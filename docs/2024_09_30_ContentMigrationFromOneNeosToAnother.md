# Content migration from one Neos instance to another one using the examples of sandstorm website relaunch 2022 and 2024

With the relaunch of our public website sandstorm.de I was tasked to migrate blog-posts, tags, podcasts, team members and
some other data from the old production instance to our new soon-to-be the new website.

## The problem
When migrating content from one to another instance, the following general steps need to happen:

1. You need to select a subset of nodes of one Neos instance, e.g. part of the document tree and all its children (all 
the documents child nodes = sub-documents and the content of the documents).
2. The target Neos needs to be prepared in such a way that all node types of the old system do exist in the new one
   (or have a replacement that understands the properties of the old node types)
3. The copied content needs to be prepared for insertion into the new system: node types and properties need to be renamed
and all paths have to be updated to correspond to the new system's paths
4. Inserting it into the new system and cleaning up
5. All media that was used on those documents needs to be transferred to the new instance and merged with the existing
files that are already present there

## The solution

There are a few ways to solve this. Below a solution is described that leaves no trace and no checked in files into git.
If you want to document what you did to make it reproducible, consider writing down your steps in a shell-script and do
the changes of node types and properties in a node migration that is checked into git as well.
Below the first solution I will attach a full example script for your reading that solves the same steps in a script. 

### 1. Selecting a subset of nodes

One way to select the nodes is:

1. Open the neos backend of the source system
2. Click on the document you want to move to the new system (for example a folder containing blog posts)
3. Use the property editor on the right side to read the node name (e.g. `node-ew3btfa0bg3rm`)
4. Filter for that via sql:
    ```sql
        SELECT * FROM neos_contentrepository_domain_model_nodedata
        WHERE path LIKE '%node-ew3btfa0bg3rm%'
    ```

5. The easiest way to manually move the content once is to use for example sequel ace and copy the resulting rows as sql
insert
6. Paste that into a temporary file or scratch pad

### 2. Prepare the target Neos to understand the copied nodes

Things to consider:

- Do all transferred node types exist in the new system? If not, the database field `nodetype` might need to be changed 
    to an equivalent node type (equivalent merely meaning that it reads the same properties as the old one)
- Allow the insertion of the transferred nodes below the node into which you want to copy them, e.g. allow insertion
  of node type `Sandstorm.Website:Document.Blog.Post` below `Sandstorm.Website:Document.Blog`
- Rename properties by searching and replacing them to match new names (e.g. in the old neos there was a property
  `sustainableGoals` that had been renamed to `sdgs` in the new system)

### 3. Prepare copied content for insertion

Neos uses the two database fields `path` and `parentpath` in the database table `neos_contentrepository_domain_model_nodedata`
to determine where a node is located in the node tree.

You will need to replace the first x parts of the old path and parent path with where you want the new content to be.
For example (both are just the beginning of a longer path string):
`/sites/website/node-575004d9c93a0/node-582afbfff1827` needs to be replace with `/sites/site/tags`

### 4. Inserting into the new system

Inserting that into the new system is straight forward:

1. !!! Create a database backup of the new system
2. Insert all nodes into the new system
3. Update the `pathhash` and `parentpathhash`. Otherwise, you will not see any nodes when reloading the Neos backend

    ```sql
        UPDATE neos_contentrepository_domain_model_nodedata SET parentpathhash = MD5(parentpath);

        UPDATE neos_contentrepository_domain_model_nodedata SET pathhash = MD5(path);
    ```

### 5. Transfer all media like images, videos, audio files

We chose to just move all resources of the old page to the new one. If you want to thin out the transferred resources first,
you might download a full dump to your local machine, boot up neos, open the backend, delete all pages that you don't 
want to transfer and then remove unused media with the flow command `./flow media:removeunused` before the next steps.

Transferring media consists of two parts:

**1. Transferring the database contents of the resources that are referenced from within the nodedata table:**

I would suggest to download the dump from the old server to your machine via the Synco tool. The reason being that Synco 
reduces the amount of transferred data to a minimum by deleting thumbnails and other data that can be regenerated on 
demand on the target system.

Then export the following tables:

```shell
mysqldump -u neos -pneos neos \
  --skip-add-drop-table --no-create-info --insert-ignore \
  neos_flow_resourcemanagement_persistentresource \
  neos_media_domain_model_adjustment_abstractimageadjustment \
  neos_media_domain_model_asset \
  neos_media_domain_model_asset_tags_join \
  neos_media_domain_model_audio \
  neos_media_domain_model_document \
  neos_media_domain_model_image \
  neos_media_domain_model_imagevariant \
  neos_media_domain_model_importedasset \
  neos_media_domain_model_tag \
  neos_media_domain_model_video \
  > media.sql
```

Insert the exported rows into your new database (!!! backup the target database first). By using `--skip-add-drop-table` 
you merge the resources that are already in the new system with the resources of the old system. 

**2. Transferring the actual files on disk from the `Data/Persistent/Resources` to the new server and merging them with
    the resources already present there**

- You can again use synco server2server or any other way to copy the resource folder to the new system
- Then you need to merge the old and the new folders.
  - If you have rsync, use it
  - If not, we succeeded creating hard links and then deleting the old folder:
    `cp --force --archive --update --link /Resources.sourceserver/. /Resources` (ATTENTION: . behind the first path needed)


## Solution example as a script for future reference:

```bash
   #!/usr/bin/env bash

echo "######## Stage 1 - Processing Dumps ########"

#grep -E -o ".{0,5}Sandstorm.Website.{0,20}" ./posts.sql

docker exec -i website-relaunch-2022-maria-db-1 /usr/bin/mysqldump -u neos -pneos neos \
     --skip-add-drop-table --no-create-info\
     neos_contentrepository_domain_model_nodedata \
     --where="path LIKE '/sites/website%node-575004d9c93a0%'" \
     --where="parentpath LIKE '/sites/website%node-575004d9c93a0/blog%'" \
     > posts.sql

sed -i '' 's/Sandstorm.Website:ContentCollection.PageMain/Sandstorm.Website:ContentCollection.Default/g' posts.sql
sed -i '' 's/Jonnitto.PrettyEmbedYoutube:Content.Youtube/Jonnitto.PrettyEmbedVideoPlatforms:Content.Video/g' posts.sql

# Rename paths
sed -i '' 's#/sites/website/node-575004d9c93a0/blog#/sites/site/node-ew3btfa0bg3rm#g' posts.sql
sed -i '' 's#/sites/website/node-575004d9c93a0#/sites/site/node-ew3btfa0bg3rm#g' posts.sql

# Rename properties
sed -i '' 's#"categories#"tags#g' posts.sql

##### Media Dump
docker exec -i website-relaunch-2022-maria-db-1 /usr/bin/mysqldump -u neos -pneos neos \
  --skip-add-drop-table --no-create-info --insert-ignore \
  neos_flow_resourcemanagement_persistentresource \
  neos_media_domain_model_adjustment_abstractimageadjustment \
  neos_media_domain_model_asset \
  neos_media_domain_model_asset_tags_join \
  neos_media_domain_model_audio \
  neos_media_domain_model_document \
  neos_media_domain_model_image \
  neos_media_domain_model_imagevariant \
  neos_media_domain_model_importedasset \
  neos_media_domain_model_tag \
  neos_media_domain_model_thumbnail \
  neos_media_domain_model_video \
  > media.sql

docker exec -i website-relaunch-2022-maria-db-1 /usr/bin/mysqldump -u neos -pneos neos \
     --skip-add-drop-table --no-create-info\
     neos_contentrepository_domain_model_nodedata \
     --where="path LIKE '/sites/website%node-575004d9c93a0%'" \
     --where="parentpath LIKE '/sites/website%node-575004d9c93a0/node-lfogj2smahx0p%'" \
     > podcasts.sql

# Rename node types
sed -i '' 's/Sandstorm.Website:Document.Blog.Post/Sandstorm.Website:Document.Podcast/g' podcasts.sql
sed -i '' 's/Sandstorm.Website:ContentCollection.PageMain/Sandstorm.Website:ContentCollection.Default/g' podcasts.sql
sed -i '' 's/Jonnitto.PrettyEmbedYoutube:Content.Youtube/Jonnitto.PrettyEmbedVideoPlatforms:Content.Video/g' podcasts.sql

# Rename paths
sed -i '' 's#/sites/website/node-575004d9c93a0/node-lfogj2smahx0p#/sites/site/node-y3l3ka0mtmm40#g' podcasts.sql

# Rename properties
sed -i '' 's#"categories#"tags#g' podcasts.sql


# Handle Tags
docker exec -i website-relaunch-2022-maria-db-1 /usr/bin/mysqldump -u neos -pneos neos \
     --skip-add-drop-table --no-create-info\
     neos_contentrepository_domain_model_nodedata \
     --where="path LIKE '/sites/website%node-575004d9c93a0%'" \
     --where="parentpath LIKE '/sites/website%node-575004d9c93a0/node-582afbfff1827%'" \
     > tags.sql

# Rename node types
#sed -i '' 's/Sandstorm.Website:Document.Blog.Tags/Sandstorm.Website:Document.Tags/g' tags.sql
#sed -i '' 's/Sandstorm.Website:Document.Blog.Tag/Sandstorm.Website:Document.Tag/g' tags.sql
sed -i '' 's/Sandstorm.Website:ContentCollection.PageMain/Sandstorm.Website:ContentCollection.Default/g' tags.sql

# Rename paths
sed -i '' 's#/sites/website/node-575004d9c93a0/node-582afbfff1827#/sites/site/tags#g' tags.sql
sed -i '' 's#/sites/website/node-575004d9c93a0#/sites/site#g' tags.sql

echo "######## Stage 2 - Importing Dumps ########"
docker exec -i website-relaunch-2024-maria-db-1 /usr/bin/mysql -u neos -pneos neos \
     < tags.sql

docker exec -i website-relaunch-2024-maria-db-1 /usr/bin/mysql -u neos -pneos neos \
     < media.sql

docker exec -i website-relaunch-2024-maria-db-1 /usr/bin/mysql -u neos -pneos neos \
     < posts.sql

docker exec -i website-relaunch-2024-maria-db-1 /usr/bin/mysql -u neos -pneos neos \
     < podcasts.sql

echo "######## Stage 3 - Finalize ########"
docker exec -i website-relaunch-2024-maria-db-1 /usr/bin/mysql -u neos -pneos neos \
     < final.sql
```
